using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.ReturnStatement)]
    public class Return : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var pmethod = parent.OfType<MethodDefinition>().SingleOrDefault();
            var isInBlock = pmethod != null && pmethod.Name.Contains("__lambda_");

            if (((ReturnStatement)node).Arguments == null || ((ReturnStatement)node).Arguments.Expressions.Count() == 0)
            {
                compiler.AppendLine("$_stack[] = new F_NilClass;");
            }
            else if (((ReturnStatement)node).Arguments.Expressions.Count() > 0)
            {
                compiler.CompileNode(((ReturnStatement)node).Arguments.Expressions.First(), parent.CreateChild(node));
            }
            else
            {
                compiler.CompileNode(new ArrayConstructor(((ReturnStatement)node).Arguments, ((ReturnStatement)node).Location), parent.CreateChild(node));
            }

            if (!isInBlock)
            {
                compiler.AppendLine("return array_pop($_stack);");
            }
            else
            {
                compiler.AppendLine("throw new ReturnFromBlock(array_pop($_stack));");
            }
        }
    }
}
