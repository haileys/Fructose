#TEST EXPECTS:
#1
#2
#3
#true
#false
#true
#false

# not much we can test here, but i'll do my best
Random.seed 1
puts Random.seed 2
puts Random.seed 3
puts Random.seed 4

puts Random.rand(10) <= 10
puts Random.rand(10) > 10

puts Random.rand <= 1.0
puts Random.rand > 1.0