#TEST EXPECTS:
#hello
#3
#true

def proc_from(&block)
    Proc.new &block
end

puts proc_from { "hello" }.call

p = Proc.new { |n| n.succ }
puts p === 2

puts p.to_s.include? "lambda"