#TEST EXPECTS:
#1
#4
#3
#nil
#nothing
#nothing

hash = { :a => 1, :b => 2 }

puts hash[:a]
hash[:c] = 4
puts hash[:c]
hash[:c] = 3
puts hash[:c]

puts hash.default(:d).inspect
hash.default = "nothing"
puts hash[:d]

hash.delete :a
puts hash[:a]

