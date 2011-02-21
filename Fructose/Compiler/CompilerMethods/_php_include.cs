using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.CompilerMethods
{
    class _php_include : CompilerMethodBase
    {
        public override void Compile(Compiler compiler, MethodCall node, NodeParent parent)
        {
            AssertParameters(node, 1);
            compiler.CompileNode(node.Arguments.Expressions.First(), parent.CreateChild(node));
            compiler.AppendLine("include array_pop($_stack)->F_to_s(NULL)->__STRING;");
        }
    }
}
