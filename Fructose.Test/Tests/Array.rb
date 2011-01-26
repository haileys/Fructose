#TEST EXPECTS:
#true
#false
#3
#false
#true

arr = [1, 2, 3]
puts arr.all? { |o| o > 0 }
puts arr.all? { |o| o > 1 }
puts arr[2]

arr = [false, false, false]
puts arr.any?
arr[1] = true
puts arr.any?