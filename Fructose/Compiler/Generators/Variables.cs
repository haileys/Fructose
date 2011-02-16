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
            compiler.AppendLine("$_stack[] = isset({0}) ? ({0}) : (new F_NilClass);", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.GlobalVariable)]
    public class GlobalVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = isset({0}) ? ({0}) : (new F_NilClass);", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.InstanceVariable)]
    public class InstanceVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = isset({0}) ? ({0}) : (new F_NilClass);", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.ClassVariable)]
    public class ClassVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = isset({0}) ? ({0}) : (new F_NilClass);", ((Variable)node).ToPHPVariable());
        }
    }

    [Generator(NodeTypes.LocalVariable)]
    public class LocalVariableGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var method = parent.OfType<MethodDefinition>().ToArray();
            if (method.Length > 0 && method[0].Parameters != null
                && method[0].Parameters.Mandatory.Where(p => p.ToString() == ((LocalVariable)node).Name).Count() > 0)
            {
                compiler.AppendLine("$_stack[] = isset(${0}) ? (${0}) : (new F_NilClass);", Mangling.RubyIdentifierToPHP(((Variable)node).Name));
                return;
            }
            if (parent.OfType<ClassDefinition>().Count() == 0)
            {
                compiler.AppendLine("$_stack[] = isset($_locals->{0}) ? ($_locals->{0}) : (new F_NilClass);", Mangling.RubyIdentifierToPHP(((Variable)node).Name));
                return;
            }
            compiler.AppendLine("$_stack[] = isset({0}) ? ({0}) : (new F_NilClass);", ((Variable)node).ToPHPVariable());
        }
    }
}
