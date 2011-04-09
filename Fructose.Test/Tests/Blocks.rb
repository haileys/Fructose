#TEST EXPECTS:
#foobar
#42
#hello world
#test
#tester
#testing

class A
  def bar
    puts yield
  end
  def foo
    local = "foobar"
    bar { local }
  end
end

A.new.foo

class B
  def setTo42
    yield 42
  end
  
  def test
    x = :test
    setTo42 { |n| x = n }
    puts x
  end
end

B.new.test

class C
  def b
    puts yield
  end
  def c
    b { yield }
  end
  def d
    c { "hello world" }
  end
end

C.new.d

def yielder(x)
  yield x
end

yielder "test" do |a|
  yielder nil do
    puts a
  end
end

def yielder_opt(x, y="er")
  yield x + y
end

yielder_opt "test" do |a|
  puts a
end

yielder_opt "test", "ing" do |a|
  puts a
end