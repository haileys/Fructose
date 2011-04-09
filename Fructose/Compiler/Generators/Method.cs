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

            if (((MethodDefinition)node).Parameters.Mandatory.Length > ((MethodDefinition)node).Parameters.LeadingMandatoryCount)
                throw new FructoseCompileException("Additional arguments after splats in methods are currently unsupported.", node);

            string visibility = parent.OfType<ClassDefinition>().Count() > 0 ?
                "public " : "";

            string signature = visibility + "function " + Mangling.RubyMethodToPHP(((MethodDefinition)node).Name) + "(";

            List<string> compiledParams = new List<string>();
            if (((MethodDefinition)node).Name.Contains("__lambda_"))
                compiledParams.Add("$_locals");
            compiledParams.Add("$block");
            foreach (var arg in ((MethodDefinition)node).Parameters.Mandatory)
                compiledParams.Add("$" + Mangling.RubyIdentifierToPHP(arg.ToString()));
            foreach (var arg in ((MethodDefinition)node).Parameters.Optional)
                compiledParams.Add("$" + Mangling.RubyIdentifierToPHP(arg.Left.ToString()) + "=NULL");
            signature += String.Join(", ", compiledParams);

            signature += ")";

            compiler.AppendLine(signature);
            compiler.AppendLine("{");
            compiler.Indent();

            compiler.AppendLine("$_stack = array();");
            compiler.AppendLine("if(!isset($_locals)) $_locals = new stdClass;");
            if (parent.OfType<ClassDefinition>().Count() > 0)
            {
                compiler.AppendLine("if(!isset($_locals->self)) $_locals->self = $this;");
            }
            else
            {
                compiler.AppendLine("global $_gthis;");
                compiler.AppendLine("if(!isset($_locals->self)) $_locals->self = $_gthis;");
            }
            compiler.AppendLine("if(!isset($_locals->block)) $_locals->block = $block;");

            compiler.AppendLine("foreach(array(" + string.Join(", ", ((MethodDefinition)node).Parameters.Mandatory
                .Select(arg => "\"" + Mangling.RubyIdentifierToPHP(arg.ToString()) + "\"")
                .ToArray()) + ") as $parm)");
            compiler.Indent();
            compiler.AppendLine("$_locals->$parm = $$parm;");
            compiler.Dedent();

            foreach (var arg in ((MethodDefinition)node).Parameters.Optional)
            {
                string parm = Mangling.RubyIdentifierToPHP(arg.Left.ToString());
                compiler.AppendLine("if($" + parm + " === NULL) {");
                compiler.Indent();
                compiler.CompileNode(arg.Right, parent.CreateChild(node));
                compiler.AppendLine("$_locals->" + parm + " = array_pop($_stack);");
                compiler.Dedent();
                compiler.AppendLine("} else {");
                compiler.Indent();
                compiler.AppendLine("$_locals->" + parm + " = $" + parm + ";");
                compiler.Dedent();
                compiler.AppendLine("}");
            }

            if (((MethodDefinition)node).Parameters.Unsplat != null)
                compiler.AppendLine("$_locals->" + Mangling.RubyIdentifierToPHP(((MethodDefinition)node).Parameters.Unsplat.ToString()) + " = "
                    + "F_Array::__from_array(array_slice(func_get_args(), " + compiledParams.Count + "));");

            compiler.AppendLine("global $_lambda_objs;");
            compiler.AppendLine("global $_globals;");

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
}
