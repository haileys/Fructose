using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.MethodCall)]
    public class MethodCallGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, IronRuby.Compiler.Ast.Node node, NodeParent parent)
        {
            if (((MethodCall)node).Arguments != null)
                foreach (var arg in ((MethodCall)node).Arguments.Expressions.Reverse())
                    compiler.CompileNode(arg, parent.CreateChild(node));

            string mname = Mangling.RubyMethodToPHP(((MethodCall)node).MethodName);

            bool callStatic = false;

            if (((MethodCall)node).Target == null)
            {
                compiler.AppendLine("$_stack[] = $this;");
            }
            else
            {
                if (((MethodCall)node).Target.NodeType == NodeTypes.ConstantVariable)
                    callStatic = true;
                else
                    compiler.CompileNode(((MethodCall)node).Target);
            }

            string call = callStatic 
                ? string.Format("$_stack[] = {0}::{1}(", Mangling.RubyIdentifierToPHP(((ConstantVariable)((MethodCall)node).Target).Name), mname)
                : string.Format("$_stack[] = array_pop($_stack)->{0}(", mname);

            if (((MethodCall)node).Arguments != null)
                for (int i = 0; i < ((MethodCall)node).Arguments.Expressions.Length; i++)
                    call += (i > 0 ? ", " : "") + "array_pop($_stack)";

            compiler.AppendLine(call + ");");
        }
    }
}
