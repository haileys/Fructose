using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.YieldCall)]
    public class Yield : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            YieldCall yield = (YieldCall)node;
            if(yield.Arguments != null)
                foreach (var arg in yield.Arguments.Expressions.Reverse())
                {
                    compiler.CompileNode(arg, parent.CreateChild(node));
                }
            compiler.AppendLine("$_blockfn = $_locals->block;");
            compiler.AppendLine("$_stack[] = $_blockfn(NULL" + string.Join("", (yield.Arguments ?? new Arguments()).Expressions.Select(ex => ", array_pop($_stack)").ToArray()) + ");");
        }
    }
}
