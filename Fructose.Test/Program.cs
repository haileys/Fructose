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

            string[] output = p.StandardOutput.ReadToEnd().Split(new[] { "\r\n", "\n" }, StringSplitOptions.RemoveEmptyEntries);
                
            int l = 0;
            foreach (var e in expects.Where(e => !string.IsNullOrEmpty(e)))
            {
                if (l == output.Length || e != output[l])
                    throw new TestFailException(string.Join("\n", output) + "\n" + p.StandardError.ReadToEnd());
                l++;
            }
        }

        static void Main(string[] args)
        {
            foreach (var file in Directory.GetFiles(TestDir, "*.rb", SearchOption.AllDirectories))
            {
                var testname = file.Remove(0, TestDir.Length);
                testname = testname.Remove(testname.Length - 2);

                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("[{0:HH:mm:ss}] Running test ", DateTime.Now);
                Console.ForegroundColor = ConsoleColor.White;
                Console.Write(testname);
                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("... ");

                try
                {
                    Test(file);
                    Console.ForegroundColor = ConsoleColor.Green;
                    Console.WriteLine("PASS");
                    Console.ForegroundColor = ConsoleColor.Gray;
                }
                catch (TestFailException e)
                {
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
