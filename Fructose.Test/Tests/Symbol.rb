#TEST EXPECTS:
#1
#true
#foo
#Foo
#foo
#FOO
#false
#3
#o

puts :foo <=> :bar
puts :foo == :foo
puts :foo
puts :foo.capitalize
puts :FoO.downcase
puts :fOo.upcase
puts :foo.empty?
puts :foo.length
puts :foo[2]