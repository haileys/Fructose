#!/usr/bin/env ruby

print "Building... "

`rm -rf bin` if Dir.exist? "bin"
["bin","bin/Fructose","bin/Fructose.Test","bin/Fructose.Test/Tests"].each { |dir| Dir.mkdir dir }

`dmcs -out:bin/Fructose/Fructose.exe -target:exe -reference:lib/IronRuby.dll -reference:lib/Microsoft.Dynamic.dll -reference:lib/Microsoft.Scripting.dll #{Dir.glob("Fructose/**/*.cs").join " "}`
`cp lib/* bin/Fructose/`

`dmcs -out:bin/Fructose.Test/Fructose.Test.exe -target:exe -reference:bin/Fructose/Fructose.exe #{Dir.glob("Fructose.Test/**/*.cs").join " "}`
`cp bin/Fructose/* bin/Fructose.Test/`
`cp Fructose.Test/Tests/* bin/Fructose.Test/Tests`
`cp libfructose/* bin/Fructose.Test`

puts "ok."

Dir.chdir "bin/Fructose.Test"
system("mono Fructose.Test.exe")
Dir.chdir "../.."