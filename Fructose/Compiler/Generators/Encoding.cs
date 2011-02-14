using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;
using IronRuby.Builtins;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.EncodingExpression)]
    public class Encoding : AstNodeGenerator
    {
		public override void Compile (Compiler compiler, Node node, NodeParent parent)
		{
			// nop
		}
	}
}