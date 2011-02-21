using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.CompilerMethods
{
    public abstract class CompilerMethodBase
    {
        public abstract void Compile(Compiler compiler, MethodCall node, NodeParent parent);

        protected static void AssertParameters(MethodCall self, int n)
        {
            if (n == 0)
            {
                if (self.Arguments != null && self.Arguments.Expressions.Length != 0)
                    throw new FructoseCompileException("Built in function " + self.MethodName + " takes no arguments", self);
            }
            else
            {
                if (self.Arguments == null || self.Arguments.Expressions.Length != 1)
                    throw new FructoseCompileException("Built in function " + self.MethodName + " takes " + n + " argument(s)", self);
            }
        }
    }
}
