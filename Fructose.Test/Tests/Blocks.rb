#TEST EXPECTS:
#foobar

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