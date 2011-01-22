using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.AndExpression)]
    public class And : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.CompileNode(((AndExpression)node).Left, parent.CreateChild(node));

            compiler.AppendLine("$_and_tmp = get_class($_stack[count($_stack)-1]);");
            compiler.AppendLine("if($_and_tmp !== 'F_NilClass' && $_and_tmp !== 'F_FalseClass')");
            compiler.AppendLine("{");
            compiler.Indent();

                compiler.CompileNode(((AndExpression)node).Left, parent.CreateChild(node));

            compiler.Dedent();
            compiler.AppendLine("}");
        }
    }
}
