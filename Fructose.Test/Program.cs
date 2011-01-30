using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using System.Diagnostics;

namespace Fructose.Test
{
    class TestFailException : Exception
    {
        public string Output { get; private set; }

        public TestFailException(string Output)
            : base("Test failed.")
        {
            this.Output = Output;
        }
    }

    class Program
    {
        const string TestDir = "Tests/";

        static void Test(string file)
        {
            List<string> expects = new List<string>();
            var input = File.ReadAllLines(file);
            if (input[0] == "#TEST EXPECTS:")
            {
                for (int i = 1; i < input.Length; i++)
                {
                    if (input[i].Length == 0 || input[i][0] != '#')
                        break;
                    expects.Add(input[i].Substring(1));
                }
            }

            var parser = new Parser(string.Join("\n", input));
            parser.Parse();
            File.Delete("tmp.php");
            File.WriteAllText("tmp.php", parser.CompileToPHP());
            var p = new Process();
            p.StartInfo = new ProcessStartInfo("php", "tmp.php") { UseShellExecute = false, RedirectStandardOutput = true, RedirectStandardError = true };
            p.Start();
            p.WaitForExit();

            var stderr = p.StandardError.ReadToEnd();
            string[] output = p.StandardOutput.ReadToEnd().Split(new[] { "\r\n", "\n" }, StringSplitOptions.RemoveEmptyEntries);
            if (!string.IsNullOrEmpty(stderr))
                throw new TestFailException("Output on STDERR:\n" + stderr + "\nSTDOUT:\n" + string.Join("\n", output));
                
            if (expects.Where(e => !string.IsNullOrEmpty(e)).Count() != output.Length)
                throw new TestFailException(string.Join("\n", output) + "\n" + p.StandardError.ReadToEnd());

            int l = 0;
            foreach (var e in expects.Where(e => !string.IsNullOrEmpty(e)))
            {
                if (e != output[l])
                    throw new TestFailException(string.Join("\n", output));
                l++;
            }
        }

        static void Main(string[] args)
        {
            foreach (var file in Directory.GetFiles(TestDir, "*.rb", SearchOption.AllDirectories).Where(
                f => { if (args.Length == 0) return true; return f.ToLower().Contains(args[0].ToLower()); }))
            {
                var testname = file.Remove(0, TestDir.Length);
                testname = testname.Remove(testname.Length - 3);

                var time = string.Format("[{0:HH:mm:ss}] [", DateTime.Now);

                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write(time);
                Console.ForegroundColor = ConsoleColor.DarkGray;
                Console.Write("WAIT");
                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("] Running test ");

                Console.ForegroundColor = ConsoleColor.White;
                Console.Write(testname);
                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("... ");

                try
                {
                    Test(file);
                    Console.Write("\r"); 
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.Write(time);
                    Console.ForegroundColor = ConsoleColor.Green;
                    Console.WriteLine("PASS");
                    Console.ForegroundColor = ConsoleColor.Gray;
                }
                catch (TestFailException e)
                {
                    Console.Write("\r");
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.Write(time);
                    Console.ForegroundColor = ConsoleColor.Red;
                    Console.WriteLine("FAIL");
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.WriteLine("Output:");
                    Console.WriteLine(e.Output);
                    return;
                }
            }
        }
    }
}
