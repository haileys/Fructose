#TEST EXPECTS:
#1
#2
#3

# not much we can test here, but i'll do my best
Random.seed 1
puts Random.seed 2
puts Random.seed 3
puts Random.seed 4

# we'll repeat this a few times to make sure it passes
n = 0
while n < 100
  if Random.rand(10) > 10
    puts "FAIL: Random.rand(10) returned value over 10"
  end
  n += 1
end

n = 0
while n < 100
  if Random.rand > 1.0
    puts "FAIL: Random.rand returned value over 1.0"
  end
  n += 1
end