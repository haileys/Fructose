using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.ConstantVariable)]
    public class ConstantVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = {0};", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.GlobalVariable)]
    public class GlobalVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = {0};", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.InstanceVariable)]
    public class InstanceVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = {0};", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.ClassVariable)]
    public class ClassVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = {0};", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.LocalVariable)]
    public class LocalVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = {0};", ((Variable)node).ToPHPVariable());
        }
    }
}
