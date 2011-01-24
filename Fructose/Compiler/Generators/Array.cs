using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.ArrayConstructor)]
    public class Array : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var ac = (ArrayConstructor)node;

            foreach (var exp in ac.Arguments.Expressions.Reverse())
                compiler.CompileNode(exp, parent.CreateChild(node));

            compiler.AppendLine("$_stack[] = F_Array::S__operator_arrayget(NULL" + string.Join("", ac.Arguments.Expressions.Select(e => ", array_pop($_stack)").ToArray()) + ");");
        }
    }
}
