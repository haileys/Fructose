#TEST EXPECTS:
#true
#false
#false
#true

arr = [1, 2, 3]
puts arr.all? { |o| o > 0 }
puts arr.all? { |o| o > 1 }

arr = [false, false, false]
puts arr.any?
arr = [false, true, false]
puts arr.any?