using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.Literal)]
    public class LiteralGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = F_Number::__from_number({0});", ((Literal)node).Value.ToString());
        }
    }
}
