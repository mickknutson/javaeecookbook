package com.baselogic.chapter05.utils;

import java.util.{Date, Locale}
import java.text.DateFormat._
import java.text.DateFormat
import java.text.SimpleDateFormat
import java.util.Calendar

object ScalaDateUtilities {
    def main(args : Array[String]) : Unit = {
	    val msg = "Hello World";
	    print(msg);
    }

    def getYesterdayDate() : String = {
        println("*** SCALA ROCK ***********************************")
        val calendar = Calendar.getInstance();
        calendar.add(Calendar.DATE, -1);
        val format = new SimpleDateFormat("yyyy-MM-dd");
        return format.format(calendar.getTime());
    }
}
