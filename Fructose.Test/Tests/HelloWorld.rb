#TEST EXPECTS:
#Hello, World!

class Greeter
  def initialize(greeting)
    @greeting = greeting
  end

  def greet!
    puts @greeting
  end
end

Greeter.new("Hello, World!").greet!