using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.MethodDefinition)]
    public class Method : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            if (parent.OfType<MethodDefinition>().Count() > 0)
                throw new FructoseCompileException("Nested methods are not supported.", node);

            if (((MethodDefinition)node).Parameters.Unsplat != null)
                throw new FructoseCompileException("Unsplats not currently supported.", node);

            if (((MethodDefinition)node).Parameters.Block != null)
                throw new FructoseCompileException("Blocks not currently supported.", node);

            if (((MethodDefinition)node).Parameters.Optional.Length > 0)
                throw new FructoseCompileException("Optional parameters not currently supported.", node);

            string visibility = parent.OfType<ClassDefinition>().Count() > 0 ?
                "public " : "";

            string signature = visibility + "function " + Mangling.RubyMethodToPHP(((MethodDefinition)node).Name) + "(";
            if (((MethodDefinition)node).Name.Contains("__lambda_"))
                signature += "$_locals, ";
            signature += "$block";
            foreach (var arg in ((MethodDefinition)node).Parameters.Mandatory)
            {
                signature += ", $" + Mangling.RubyIdentifierToPHP(arg.ToString());
            }
            signature += ")";

            compiler.AppendLine(signature);
            compiler.AppendLine("{");
            compiler.Indent();
            compiler.AppendLine("$_stack = array();");
            compiler.AppendLine("if(!isset($_locals->self)) $_locals->self = $this;");
            compiler.AppendLine("global $_lambda_objs;");

                foreach (var child in ((MethodDefinition)node).Body.Statements)
                    compiler.CompileNode(child, parent.CreateChild(node));

            if (((MethodDefinition)node).Name.Contains("__lambda_"))
                compiler.AppendLine("return array( 'locals' => $_locals, 'retval' => array_pop($_stack) );");
            else
                compiler.AppendLine("return array_pop($_stack);");

            compiler.Dedent();
            compiler.AppendLine("}");
        }
    }

    /*
void CompileMethod(StringBuilder sb, PHP.Method method, string indentlevel = "")
{
    sb.Append(string.Format("{0}{1} function {2}(", indentlevel, method.VisibilityModifier, method.Name));
    bool first = true;
    foreach (var arg in method.Arguments.Mandatory)
    {
        sb.Append(string.Format("{0} ${1}", first ? "" : ",", Mangling.RubyIdentifierToPHP(arg.ToString())));
        first = false;
    }
    sb.AppendLine(string.Format(")\n{0}{{", indentlevel));

    foreach (var globref in method.GlobalReferences)
        sb.AppendLine(string.Format("{0}{1}global $_global_{2};", indentlevel, Indent, Mangling.RubyIdentifierToPHP(globref.Name)));

    sb.AppendLine(string.Format("{0}{1}$_stack = array();", indentlevel, Indent));

    foreach(var node in method.RubyStatements)
        compileRec(sb, node, indentlevel + Indent);

    sb.AppendLine(string.Format("{0}{1}return array_pop($_stack);", indentlevel, Indent));
    sb.AppendLine(string.Format("{0}}}", indentlevel));
}
*/
}
