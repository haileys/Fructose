#TEST EXPECTS:
#1
#4
#3
#nil
#nothing
#nothing
#3
#nil
#true
#false
#true
#false
#b
#2
#a
#a
#b
#c

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

hash = { :a => 1, :b => 2, :c => 3, :d => 4, :e => 5 }
hash.delete_if { |k,v| v >= 4 }
puts hash.size
puts hash[:d].inspect
puts hash.has_key? :b
puts hash.has_key? :d
puts hash.has_value? 2
puts hash.has_value? 4

puts hash.invert[2]

hash.keep_if { |k,v| v < 3 }
puts hash.size

puts hash.key 1

hash.merge! :b => 3, :c => 3
hash.keys.each { |k| puts k }