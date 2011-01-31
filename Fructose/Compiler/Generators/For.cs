using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.ForLoopExpression)]
    public class For : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var forexp = (ForLoopExpression)node;
            compiler.CompileNode(forexp.List, parent.CreateChild(node));
            compiler.AppendLine("$_tmp_enumerator[] = array_pop($_stack)->F_each(NULL);");
            compiler.AppendLine("while(true)");
            compiler.AppendLine("{");
            compiler.Indent();
            compiler.AppendLine("try");
            compiler.AppendLine("{");
            compiler.Indent();
            compiler.AppendLine("$_forcur = $_tmp_enumerator[count($_tmp_enumerator)-1]->F_next(NULL);");
            compiler.Dedent();
            compiler.AppendLine("}");
            compiler.AppendLine("catch(ErrorCarrier $err)");
            compiler.AppendLine("{");
            compiler.Indent();
            compiler.AppendLine("if(is_a($err->val, 'F_StopIteration'))");
            compiler.Indent();
            compiler.AppendLine("break;");
            compiler.Dedent();
            compiler.Dedent();
            compiler.AppendLine("}");

            if (forexp.Block.Parameters.Mandatory.Length == 1)
            {
                compiler.AppendLine("{0} = $_forcur;", forexp.Block.Parameters.Mandatory.Cast<Variable>().Single().ToPHPVariable());
            }
            else
            {
                foreach (var var in forexp.Block.Parameters.Mandatory.Cast<Variable>())
                {
                    compiler.AppendLine("{0} = array_shift($_forcur->__ARRAY);", var.ToPHPVariable());
                }
            }

            foreach (var stmt in forexp.Block.Body)
                compiler.CompileNode(stmt, parent.CreateChild(node));

            compiler.Dedent();
            compiler.AppendLine("}");
        }
    }
}
