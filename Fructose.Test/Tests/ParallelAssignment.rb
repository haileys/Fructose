#TEST EXPECTS:
#b
#a
#nil
#3
#2
#1
#F_Array
#F_Array

a = :a
b = :b
a,b = b,a
puts a
puts b

a,b,c = a,b
puts c.inspect

x = [1,2,3]
a,b,c = x
puts c
puts b
puts a

a,b = [1,2],[3,4]
puts a.class
puts b.class