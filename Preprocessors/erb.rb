require 'erb'

#
# run like this:
# ruby Preprocessors/erb.rb IN_FILE | mono Fructose.exe -o OUT_FILE -
#

puts ERB.new($<.read).src
puts "puts _erbout"