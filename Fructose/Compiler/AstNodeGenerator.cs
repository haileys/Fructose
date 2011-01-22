using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler
{
    public class GeneratorAttribute : Attribute
    {
        public NodeTypes NodeType { get; private set; }

        public GeneratorAttribute(NodeTypes NodeType)
        {
            this.NodeType = NodeType;
        }
    }

    public abstract class AstNodeGenerator
    {
        public abstract void Compile(Compiler compiler, Node node, NodeParent parent);
    }
}
