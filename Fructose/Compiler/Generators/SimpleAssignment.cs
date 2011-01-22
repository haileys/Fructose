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
            compiler.AppendLine("{0} = $_stack[count($_stack)-1];", ((Variable)((SimpleAssignmentExpression)node).Left).ToPHPVariable());
        }
    }
}
