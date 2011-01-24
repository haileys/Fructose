#TEST EXPECTS:
#true
#false

arr = [1, 2, 3]
puts arr.all? { |o| o > 0 }
puts arr.all? { |o| o > 1 }