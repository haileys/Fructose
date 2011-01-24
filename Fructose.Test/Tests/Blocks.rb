#TEST EXPECTS:
#foobar
#42

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