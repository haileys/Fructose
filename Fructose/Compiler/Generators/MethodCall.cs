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
                compiler.AppendLine("$_stack[] = $_locals->self;");
            }
            else
            {
                if (((MethodCall)node).Target.NodeType == NodeTypes.ConstantVariable)
                    callStatic = true;
                else
                    compiler.CompileNode(((MethodCall)node).Target, parent.CreateChild(node));
            }
            
            if (((MethodCall)node).Block != null)
            {
                compiler.AppendLine("$_lambda_objs_offset = count($_lambda_objs);");
                compiler.AppendLine("$_lambda_objs[] = $_locals;");
            }

            if (((MethodCall)node).Block != null)
            {
                compiler.AppendLine("try");
                compiler.AppendLine("{");
                compiler.Indent();
            }

            string call = callStatic 
                ? string.Format("$_stack[] = {0}::S{1}(", Mangling.RubyIdentifierToPHP(((ConstantVariable)((MethodCall)node).Target).Name), mname)
                : string.Format("$_stack[] = array_pop($_stack)->{0}(", mname);

            if (((MethodCall)node).Block != null)
            {
                if (((MethodCall)node).Block.NodeType == NodeTypes.BlockReference)
                {
                    var expr = ((BlockReference)((MethodCall)node).Block).Expression;
                    if(expr.NodeType != NodeTypes.LocalVariable
                        && parent.OfType<MethodDefinition>().Count() != 1
                        && parent.OfType<MethodDefinition>().Single().Parameters != null
                        && parent.OfType<MethodDefinition>().Single().Parameters.Block != null
                        && ((LocalVariable)expr).Name != parent.OfType<MethodDefinition>().Single().Parameters.Block.Name)
                        throw new FructoseCompileException("Block references not referring to a block parameter are not yet supported.", ((MethodCall)node).Block);

                    call += "$block";
                }
                else
                {
                    var block_mname = compiler.Transformations.RefactoredBlocksToMethods[(BlockDefinition)((MethodCall)node).Block];

                    call += "create_function('',sprintf('global $_lambda_objs; $args = func_get_args(); $offset = %d; $_locals = $_lambda_objs[$offset]; array_unshift($args, $_locals); $r = call_user_func_array(";

                    if (parent.OfType<ClassDefinition>().Count() > 0)
                        call += "array($_locals->self,\"" + Mangling.RubyIdentifierToPHP(block_mname) + "\")";
                    else
                        call += "\"" + Mangling.RubyIdentifierToPHP(block_mname) + "\"";

                    call += ", $args); $_lambda_objs[$offset] = $r[\"locals\"]; return $r[\"retval\"];',$_lambda_objs_offset))";
                }
            }
            else
                call += "NULL";

            if (((MethodCall)node).Arguments != null)
            {
                for (int i = 0; i < ((MethodCall)node).Arguments.Expressions.Length; i++)
                    call += ", array_pop($_stack)";
            }

            compiler.AppendLine(call + ");");

            if (((MethodCall)node).Block != null)
            {
                compiler.Dedent();
                compiler.AppendLine("}");
                compiler.AppendLine("catch(ReturnFromBlock $rfb)");
                compiler.AppendLine("{");
                compiler.Indent();
                compiler.AppendLine("return $rfb->val;");
                compiler.Dedent();
                compiler.AppendLine("}");
                compiler.AppendLine("$_locals = $_lambda_objs[$_lambda_objs_offset];");
            }
        }
    }
}
