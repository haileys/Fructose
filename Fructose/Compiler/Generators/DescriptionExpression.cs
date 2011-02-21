using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.AstNodeDescriptionExpression)]
    public class DescriptionExpressionGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            if (node is IsDefinedExpression)
            {
                var ide = (IsDefinedExpression)node;
                switch(ide.Expression.NodeType)
                {
                    case NodeTypes.MethodCall:
                        compiler.AppendLine("$_stack[] = F_TrueClass::__from_bool(function_exists('{0}'));", Mangling.RubyMethodToPHP(((MethodCall)ide.Expression).MethodName));
                        return;
                    case NodeTypes.ConstantVariable:
                        compiler.AppendLine("$_stack[] = F_TrueClass::__from_bool(class_exists('{0}'));", Mangling.RubyMethodToPHP(((ConstantVariable)ide.Expression).Name));
                        return;
                    case NodeTypes.ClassVariable:
                    case NodeTypes.GlobalVariable:
                    case NodeTypes.InstanceVariable:
                    case NodeTypes.LocalVariable:
                        compiler.AppendLine("$_stack[] = F_TrueClass::__from_bool(isset({0}));", ((Variable)ide.Expression).ToPHPVariable());
                        return;
                    default:
                        throw new FructoseCompileException("Not supported yet: defined?( " + ide.Expression.NodeType.ToString() + " )", ide.Expression);
                }
                
            }
            else
                throw new FructoseCompileException("Not supported yet: " + node.ToString(), node);
        }
    }
}
