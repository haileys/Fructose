using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.Body)]
    public class BodyGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var body = (Body)node;
            if (body.RescueClauses != null)
            {
                for(int i = 0; i < body.RescueClauses.Count; i++)
                {
                    compiler.AppendLine("try");
                    compiler.AppendLine("{");
                    compiler.Indent();
                }
            }

            foreach (var stmt in body.Statements)
                compiler.CompileNode(stmt, parent.CreateChild(node));

            if (body.RescueClauses != null)
            {
                foreach (var rescue in body.RescueClauses)
                {
                    compiler.Dedent();
                    compiler.AppendLine("}");
                    compiler.AppendLine("catch(ErrorCarrier $err)");
                    compiler.AppendLine("{");
                    compiler.Indent();
                    StringBuilder clause = new StringBuilder();
                    bool first = true;
                    foreach (var t in rescue.Types)
                    {
                        if (t.NodeType != NodeTypes.ConstantVariable)
                            throw new FructoseCompileException("Type constant must be supplied in rescue expression", rescue);

                        if (!first)
                            clause.Append(" && ");
                        first = false;
                        clause.Append(string.Format("!is_a($err->val, '{0}')", Mangling.RubyIdentifierToPHP(((ConstantVariable)t).Name)));
                    }
                    compiler.AppendLine("if({0})", clause.ToString());
                    compiler.Indent();
                    compiler.AppendLine("throw $err;");
                    compiler.Dedent();
                    compiler.AppendLine("{0} = $err->val;", ((Variable)(rescue.Target ?? new GlobalVariable("!", rescue.Location))).ToPHPVariable());

                    foreach (var stmt in rescue.Statements)
                        compiler.CompileNode(stmt, parent.CreateChild(node));

                    compiler.Dedent();
                    compiler.AppendLine("}");
                }
            }
        }
    }
}
