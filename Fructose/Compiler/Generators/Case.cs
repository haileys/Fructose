using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.CaseExpression)]
    public class Case : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            CaseExpression c = (CaseExpression)node;
			compiler.CompileNode(c.Value, parent.CreateChild(node));
			compiler.AppendLine("$_caseval = array_pop($_stack);");
			
			foreach(var when in c.WhenClauses)
			{
				var sb = new StringBuilder();
				compiler.AppendLine("$_comparisons = array();");
				foreach(var compar in when.Comparisons)
				{
					if(sb.Length > 0)
						sb.Append(" || ");
					sb.Append("_isTruthy(array_pop($_comparisons))");
					compiler.CompileNode(compar, parent.CreateChild(node).CreateChild(when));
					compiler.AppendLine("$_comparisons[] = array_pop($_stack)->__operator_stricteq(NULL, $_caseval);");
				}
				compiler.AppendLine("if({0})", sb.ToString());
				compiler.AppendLine("{");
				compiler.Indent();
				foreach(var stmt in when.Statements)
					compiler.CompileNode(stmt, parent.CreateChild(node).CreateChild(when));
				compiler.Dedent();
				compiler.AppendLine("}");
				compiler.AppendLine("else");
				compiler.AppendLine("{");
				compiler.Indent();
			}
			if(c.ElseStatements != null)
				foreach(var stmt in c.ElseStatements)
					compiler.CompileNode(stmt, parent.CreateChild(node));
			for(int i = 0; i < c.WhenClauses.Length; i++)
			{
				compiler.Dedent();
				compiler.AppendLine("}");
			}
        }
    }
}