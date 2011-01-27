#TEST EXPECTS:
#true
#true
#false
#false
#true

puts true if 1 and 2
puts true if nil or true
puts false unless false and true
puts false unless false or nil
puts true if not false