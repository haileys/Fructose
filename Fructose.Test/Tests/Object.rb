#TEST EXPECTS:
#false
#false
#true
#true
#false
#false
#2
#false
#true
#false
#hi
#Hi
#true
#false

a = "hi"
puts a.untrusted?
puts a.tainted?
a.taint
a.untrust
puts a.untrusted?
puts a.tainted?
a.untaint
a.trust
puts a.untrusted?
puts a.tainted?

b = [1, 2, 3]
c = b.dup
puts c[1]

puts b.instance_of? :Enumerable
puts b.is_a? :Enumerable
puts b.nil?

a.tap{|x| puts x}.capitalize.tap{|x| puts x}.upcase

puts a.respond_to? :nil?
puts a.send :nil?