using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler
{
    public class NodeParent : IEnumerable<Node>
    {
        public Node Node { get; private set; }
        public NodeParent Parent { get; private set; }

        public bool IsTerminator { get; private set; }

        public static NodeParent Terminator { get { return new NodeParent(null) { IsTerminator = true }; } }

        public NodeParent(Node node)
        {
            Parent = null;
            Node = node;
        }

        public NodeParent CreateChild(Node node)
        {
            return new NodeParent(node) { Parent = this };
        }

        public IEnumerator<Node> GetEnumerator()
        {
            for (NodeParent n = this; !n.IsTerminator; n = n.Parent)
                yield return n.Node;
        }

        System.Collections.IEnumerator System.Collections.IEnumerable.GetEnumerator()
        {
            return (this as IEnumerable<Node>).GetEnumerator();
        }
    }
}
