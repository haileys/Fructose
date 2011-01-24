using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.SimpleAssignmentExpression)]
    public class SimpleAssignment : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, IronRuby.Compiler.Ast.Node node, NodeParent parent)
        {
            compiler.CompileNode(((SimpleAssignmentExpression)node).Right, parent.CreateChild(node));

            if (((SimpleAssignmentExpression)node).Left is LocalVariable)
            {
                var method = parent.OfType<MethodDefinition>().ToArray();
                if (method.Length > 0 && method[0].Parameters != null
                    && method[0].Parameters.Mandatory.Where(p => p.ToString() == ((LocalVariable)((SimpleAssignmentExpression)node).Left).Name).Count() > 0)
                {
                    compiler.AppendLine("$_stack[] = ${0};", Mangling.RubyIdentifierToPHP(((LocalVariable)((SimpleAssignmentExpression)node).Left).Name));
                    return;
                }
                if (parent.OfType<ClassDefinition>().Count() == 0)
                {
                    compiler.AppendLine("$_locals->{0} = $_stack[count($_stack)-1];", Mangling.RubyIdentifierToPHP(((LocalVariable)((SimpleAssignmentExpression)node).Left).Name));
                    return;
                }
            }

            compiler.AppendLine("{0} = $_stack[count($_stack)-1];", ((Variable)((SimpleAssignmentExpression)node).Left).ToPHPVariable());
        }
    }
}
