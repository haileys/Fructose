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
    41
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