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
            var sae = (SimpleAssignmentExpression)node;

            switch (sae.Left.NodeType)
            {
                case NodeTypes.ArrayItemAccess:
                    var aia = (ArrayItemAccess)sae.Left;
                    // substitute a method call of []= rather than the crap that ironruby's parser produces
                    compiler.CompileNode(new MethodCall(aia.Array, "[]=", new Arguments(aia.Arguments.Expressions.Concat(new[] { sae.Right }).ToArray()), sae.Location), parent);
                    return;
            }

            compiler.CompileNode(sae.Right, parent.CreateChild(node));

            if (((SimpleAssignmentExpression)node).Left is LocalVariable)
            {
                var method = parent.OfType<MethodDefinition>().ToArray();
                if (method.Length > 0 && method[0].Parameters != null
                    && method[0].Parameters.Mandatory.Where(p => p.ToString() == ((LocalVariable)sae.Left).Name).Count() > 0)
                {
                    compiler.AppendLine("$_stack[] = ${0};", Mangling.RubyIdentifierToPHP(((LocalVariable)sae.Left).Name));
                    return;
                }
                if (parent.OfType<ClassDefinition>().Count() == 0)
                {
                    compiler.AppendLine("$_locals->{0} = $_stack[count($_stack)-1];", Mangling.RubyIdentifierToPHP(((LocalVariable)sae.Left).Name));
                    return;
                }
            }

            compiler.AppendLine("{0} = $_stack[count($_stack)-1];", ((Variable)sae.Left).ToPHPVariable());
        }
    }
}
