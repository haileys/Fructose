using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;

namespace Fructose
{
    class Program
    {
        static Stream input = null;
        static Stream output = null;

        static void Main(string[] args)
        {
            try
            {
                ParseArgs(args);
            }
            catch (FileNotFoundException ex)
            {
                Fatal("No such file: {0}", ex.FileName);
            }

            if (input == null)
                Usage();

            using (var sr = new StreamReader(input))
            {
                var source = sr.ReadToEnd();
                var translator = new Parser(source);
                translator.Parse();
                using (var sw = new StreamWriter(output))
                {
                    sw.Write(translator.CompileToPHP(source));
                    sw.Flush();
                }
            }
        }

        static void Usage()
        {
            Console.WriteLine(@"Fructose - Ruby to PHP compiler.

Usage: fructose [( -o output-file | --stdout )] ( - | input-file )
");
        }

        static void Fatal(string message, params object[] args)
        {
            Console.WriteLine("[fructose] {0}", string.Format(message, args));
            Environment.Exit(1);
        }

        static void ParseArgs(string[] args)
        {
            string default_out_name = "out.php";

            for (int i = 0; i < args.Length; i++)
            {
                switch (args[i])
                {
                    case "-o":
                        if (++i == args.Length)
                            Fatal("Expected filename after -o");

                        output = File.OpenWrite(args[i]);
                        break;
                    case "--stdout":
                        output = Console.OpenStandardOutput();
                        break;
                    case "-":
                        input = Console.OpenStandardInput();
                        break;
                    default:
                        if (args[i][0] == '-')
                            Fatal("Unknown option {0}", args[i]);
                        if (input != null)
                            Fatal("Multiple input files are not supported");
                        input = File.OpenRead(args[i]);
                        default_out_name = Path.ChangeExtension(args[i], "php");
                        break;
                }
            }

            if (output == null)
                output = File.Open(default_out_name, FileMode.Create);
        }
    }
}
