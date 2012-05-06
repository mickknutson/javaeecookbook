package cucumber.examples.java.calculator;

import cucumber.DateFormat;
import cucumber.annotation.en.Given;
import cucumber.annotation.en.Then;
import cucumber.annotation.en.When;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.Date;

import static org.junit.Assert.assertEquals;

public class DateStepdefs {

    private static final Logger logger = LoggerFactory.getLogger(DateStepdefs.class);

    private String result;
    private DateCalculator calculator;

    @Given("^today is (.+)$")
    public void today_is(@DateFormat("yyyy-MM-dd") Date date) {
        System.out.println("----->DateStepdefs System.out.println");
        logger.debug("----->debug");
        logger.info("----->info");
        logger.warn("----->warn");
        logger.error("----->error");
        calculator = new DateCalculator(date);
    }

    @When("^I ask if (.+) is in the past$")
    public void I_ask_if_date_is_in_the_past(Date date) {
        System.out.println("----->DateStepdefs System.out.println");
        logger.debug("----->debug");
        logger.info("----->info");
        logger.warn("----->warn");
        logger.error("----->error");
        result = calculator.isDateInThePast(date);
    }

    @Then("^the result should be (.+)$")
    public void the_result_should_be(String expectedResult) {
        System.out.println("----->DateStepdefs System.out.println");
        logger.debug("----->debug");
        logger.info("----->info");
        logger.warn("----->warn");
        logger.error("----->error");
        assertEquals(expectedResult, result);
    }
}