using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.ClassDefinition)]
    public class Class : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            if (parent.OfType<ClassDefinition>().Count() > 0)
                throw new FructoseCompileException("Nested classes are not supported.", node);

            if (((ClassDefinition)node).SuperClass != null && ((ClassDefinition)node).SuperClass.NodeType != NodeTypes.ConstantVariable)
                throw new FructoseCompileException("Classes may only inherit from constant expressions", node);

            var super = (ConstantVariable)((ClassDefinition)node).SuperClass;

            var cname = Mangling.RubyIdentifierToPHP(((ClassDefinition)node).QualifiedName.Name);

            compiler.AppendLine("class {0} extends {1}", cname,
                super == null ? "F_Object" : Mangling.RubyIdentifierToPHP(super.Name));
            compiler.AppendLine("{");

            compiler.Indent();

            compiler.AppendLine("public static function F_new()");
            compiler.AppendLine("{");
            compiler.Indent();
            compiler.AppendLine("$obj = new {0};", cname);
            compiler.AppendLine("$args = func_get_args();");
            compiler.AppendLine("call_user_func_array(array($obj,'F_initialize'), $args);");
            compiler.AppendLine("return $obj;");
            compiler.Dedent();
            compiler.AppendLine("}");

            foreach (var child in ((ClassDefinition)node).Body.Statements)
            {
                if (child.NodeType == NodeTypes.MethodDefinition)
                {
                    compiler.CompileNode(child, parent.CreateChild(node));
                    continue;
                }

                throw new FructoseCompileException("Classes may only contain method declarations at this point", node);
            }

            compiler.Dedent();
            compiler.AppendLine("}");
        }
    }
}
