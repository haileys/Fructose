#TEST EXPECTS:
#false
#true
#false
#true

puts defined?(x)
puts defined?(Object)
x = 123
puts defined?(Foo)
puts defined?(x)