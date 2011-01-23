# Fructose

**Fructose is a language that compiles to PHP.** Fructose's syntax is borrowed from that of Ruby's, with changes and standard library differences to make this project feasible under PHP.

It is composed of two parts - Fructose itself, which is written in C#, and libfructose - a support library written in PHP and included by every file Fructose outputs. libfructose is designed to provide a subset of the Ruby standard library to Fructose programs.

### Requirements

Fructose is written in C# and requires the .NET 4.0 framework to run. Fructose **does** work and is supported under Mono, but you will need to find yourself a copy of Microsoft.Scripting.dll and Microsoft.Dynamic.dll to get it to compile. Fructose also requires the presence of IronRuby to compile and run.

### Licensing

The Fructose compiler is licensed under the New BSD license. libfructose is licensed under the zlib license.