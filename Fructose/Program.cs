using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;

namespace Fructose
{
    class Program
    {
        static string filetype = null;
        static Stream input = null;
        static Stream output = null;
        static string outputPath = null;
        static bool forceCompile = false;

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

                if (!forceCompile && outputPath != null && File.Exists(outputPath))
                {
                    using (var sr2 = new StreamReader(File.Open(outputPath, FileMode.Open)))
                    {
                        sr2.ReadLine();
                        if (source.MD5() == sr2.ReadLine().Replace("// ", ""))
                            Fatal("Skipping compile as MD5 matches");
                    }
                }

                Parser translator = null;
                switch (filetype)
                {
                    case ".rb": translator = new Parser(source); break;
                    case ".erb": translator = new ErbParser(source); break;
                    default: Fatal("Filetype {0} not supported", filetype); break;
                }
                translator.Parse();

                output = File.Open(outputPath, FileMode.Create);
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

Usage: fructose [--force] [(-f|--filetype) ( rb | erb )] [( -o output-file | --stdout )] ( - | input-file )
");
			Environment.Exit(1);
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
                    case "--force":
                        forceCompile = true;
                        break;

                    case "-f":
                    case "--filetype":
                        filetype = "." + args[i + 1];
                        i++;
                        break;

                    case "-o":
                        if (++i == args.Length)
                            Fatal("Expected filename after -o");
                        outputPath = args[i];
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
                        input = File.Open(args[i], FileMode.Open);
                        if (filetype == null)
                            filetype = Path.GetExtension(args[i]);
                        default_out_name = Path.ChangeExtension(args[i], "php");
                        break;
                }
            }

            if (outputPath == null && output == null)
                outputPath = default_out_name;
        }
    }
}
