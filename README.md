# Fructose

Fructose is a compiler designed to compile a subset of Ruby to the PHP programming language.

It is composed of two parts - Fructose itself, which is written in C#, and libfructose - a support library written in PHP and included by every file Fructose outputs. libfructose is designed to provide a subset of the Ruby standard library to Fructose programs.

### Requirements

Fructose is written in C# and requires the .NET 4.0 framework to run. Fructose most likely works under Mono. Fructose also requires the presence of IronRuby to compile and run.

### Licensing

The Fructose compiler is licensed under the New BSD license. libfructose is licensed under the zlib license.