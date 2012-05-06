package calc

import calc.Calculator
import java.util.logging.Logger

this.metaClass.mixin(cucumber.runtime.groovy.Hooks)
this.metaClass.mixin(cucumber.runtime.groovy.EN)

class CustomWorld {
    String customMethod() {
        "foo"
    }
}

World {

    def log
    World() {
        log=Logger.getLogger(this.class.name)
    }
    new CustomWorld()
}

Before() {
    //assert "foo" == customMethod()
    calc = new Calculator()
}

Before("@notused") {
    throw new RuntimeException("Never happens")
}

Before("@notused,@important", "@alsonotused") {
    throw new RuntimeException("Never happens")
}

Given(~"I have entered (\\d+) into (.*) calculator") { int number, String ignore ->
    calc.push number
}

Given(~"(\\d+) into the") {->
    throw new RuntimeException("should never get here since we're running with --guess")
}

When(~"I press (\\w+)") { String opname ->
    System.out.println("----->CalculatorSteps System.out.println");
    println("----->CalculatorSteps println");
    result = calc."$opname"()
}

Then(~"the stored result should be (.*)") { double expected ->
    assert expected == result
}
