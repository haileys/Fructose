using System;
using System.Linq;
using System.Reflection;

namespace Fructose
{
	public abstract class Preprocessor
	{
		public abstract string Process(string input);
		
		public static Preprocessor GetByName(string Name)
		{
			throw new NotImplementedException("brb");
			return null;
		}
	}
}

