using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.RangeExpression)]
    public class Range : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var re = (RangeExpression)node;
            compiler.CompileNode(re.End, parent.CreateChild(node));
            compiler.CompileNode(re.Begin, parent.CreateChild(node));
            compiler.AppendLine("$_stack[] = F_Range::SF_new(NULL, array_pop($_stack), array_pop($_stack), new F_{0}Class);", re.IsExclusive.ToString());
        }
    }
}
