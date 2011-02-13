#TEST EXPECTS:
#true
#false
#3
#false
#true
#false
#3
#42
#2
#2
#3
#4
#true
#true
#3
#3
#12
#1
#6
#1
#6
#3
#9
#12

arr = [1, 2, 3]
puts arr.all? { |o| o > 0 }
puts arr.all? { |o| o > 1 }
puts arr[2]

arr = [false, false, false]
puts arr.any?
arr[1] = true
puts arr.any?

puts arr.collect{ |o| not o }[1]

puts arr.count

# this'll make sure it calls size if the class responds to it
# A#each isn't defined so count must call size, or else it'll fail.
class A < Enumerable
  def size
    42
  end
end
puts A.new.count

# size isn't defined so it has to call each
class B < Enumerable
  def each
    yield
    yield
  end
end
puts B.new.count

arr = [1, 2, 4, 2]

puts arr.count 2
puts arr.count { |n| n > 1 }

puts arr.find { |n| n > 3 }
puts arr.find { |n| n > 4 }.nil?

arr = arr.drop 2
puts arr.count == arr[1]

arr = [1, 2, 3, 4, 5, 6]
puts arr.drop_while { |n| n <= 3 }.count

puts arr.find { |n| n > 2 }

puts arr.select { |n| n.even? } .reduce(:+)
puts arr.first
puts arr.max
puts arr.min
puts arr.sort { |a,b| -(a <=> b) }.first
puts arr.take(3).max

zipped = [1,2].zip([3,4],[5,6])

zipped.each do |z|
  puts z.reduce :+
end