# Fructose

**Fructose is a language that compiles to PHP.** Fructose's syntax is borrowed from that of Ruby's, with changes and standard library differences to make this project feasible under PHP.

It is composed of two parts - Fructose itself, which is written in C#, and libfructose - a support library written in PHP and included by every file Fructose outputs. libfructose is designed to provide a subset of the Ruby standard library to Fructose programs.

### libfructose Status

<table>
<tr><td>Enumerable</td><td><b>Done</b></td></tr>
<tr><td>Array</td><td><b>Done</b></td></tr>
<tr><td>String</td><td><b>Done</b></td></tr>
<tr><td>Symbol</td><td><b>Done</b></td></tr>
<tr><td>Number</td><td><b>Done</b></td></tr>
<tr><td>TrueClass/FalseClass</td><td><b>Done</b></td></tr>
<tr><td>NilClass</td><td><b>Done</b></td></tr>
<tr><td>Object (including Kernel)</td><td><b>Done</b></td></tr>
<tr><td>Hash</td><td><b>Done</b></td></tr>
<tr><td>Exceptions/Errors</td><td><b>Done</b></td></tr>
<tr><td>Regexp</td><td></td></tr>
<tr><td>Match</td><td></td></tr>
<tr><td>Proc</td><td><b>Done</b></td></tr>
<tr><td>Random</td><td><b>Done</b></td></tr>
<tr><td>Range</td><td></td></tr>
<tr><td>Time</td><td></td></tr>
<tr><td>Dir</td><td></td></tr>
<tr><td>File</td><td></td></tr>
</table>

### Requirements

Fructose is written in C# and requires the .NET 4.0 framework to run. Fructose **does** work and is supported under Mono, but you will need to find yourself a copy of Microsoft.Scripting.dll and Microsoft.Dynamic.dll to get it to compile. Fructose also requires the presence of IronRuby to compile and run.

### Licensing

The Fructose compiler is licensed under the New BSD license. libfructose is licensed under the zlib license.