using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose
{
    public static class Mangling
    {
        public static string RubyIdentifierToPHP(string ident)
        {
            return "F_" + ident.Replace("!", "_EXCL_").Replace("?", "_QUES_");
        }

        public static string ToPHPVariable(this Variable var)
        {
            switch (var.NodeType)
            {
                case NodeTypes.ConstantVariable:
                    return string.Format("{0}", Mangling.RubyIdentifierToPHP(((LocalVariable)var).Name));
                case NodeTypes.GlobalVariable:
                    return string.Format("$_global_{0}", Mangling.RubyIdentifierToPHP(((GlobalVariable)var).Name));
                case NodeTypes.LocalVariable:
                    return string.Format("$_locals->{0}", Mangling.RubyIdentifierToPHP(((LocalVariable)var).Name));
                case NodeTypes.InstanceVariable:
                    return string.Format("$_locals->self->_instance_vars[\"{0}\"]", Mangling.RubyIdentifierToPHP(((InstanceVariable)var).Name));
                case NodeTypes.ClassVariable:
                    return string.Format("$_locals->self->_class_vars[\"{0}\"]", Mangling.RubyIdentifierToPHP(((ClassVariable)var).Name));
                default:
                    throw new Compiler.FructoseCompileException("Unknown variable type", var);
            }
        }

        public static string RubyMethodToPHP(string mname)
        {
            switch (mname)
            {
                case "!": return "__operator_not";
                case "~": return "__operator_bitwisenot";
                case "+@": return "__operator_unaryplus";
                case "**": return "__operator_exp";
                case "-@": return "__operator_unaryminus";
                case "*": return "__operator_mul";
                case "/": return "__operator_div";
                case "%": return "__operator_mod";
                case "+": return "__operator_add";
                case "-": return "__operator_sub";
                case "<<": return "__operator_lshift";
                case ">>": return "__operator_rshift";
                case "&": return "__operator_bitwiseand";
                case "|": return "__operator_bitwiseor";
                case "^": return "__operator_xor";
                case "<": return "__operator_lt";
                case "<=": return "__operator_lte";
                case ">": return "__operator_gt";
                case ">=": return "__operator_gte";
                case "==": return "__operator_eq";
                case "===": return "__operator_stricteq";
                case "!=": return "__operator_neq";
                case "=~": return "__operator_match";
                case "!~": return "__operator_notmatch";
                case "<=>": return "__operator_spaceship";
                case "[]": return "__operator_arrayget";
                case "[]=": return "__operator_arrayset";

                default:
                    if (mname.Last() == '=')
                        return mname.Substring(0, mname.Length - 1) + "__set";

                    return RubyIdentifierToPHP(mname);
            }
        }
    }
}
