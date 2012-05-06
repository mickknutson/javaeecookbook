package com.baselogic.javaee6;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.servlet.ServletException;
//import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.UUID;

/**
 * Created by IntelliJ IDEA.
 * User: MKnuts6173c
 * Date: 11/1/11
 * Time: 1:10 PM
 * To change this template use File | Settings | File Templates.
 */
//@WebServlet(name = "requestProcessorServlet", urlPatterns = { "/*" })
public class RequestProcessorServlet extends HttpServlet {

    private static final Logger logger = LoggerFactory.getLogger(RequestProcessorServlet.class);

    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        logger.info("RequestProcessorServlet.doPost()");

        response.setContentType("text/html");
        PrintWriter out = response.getWriter();

        //String name = response.getParameter("name");
        out.println("RequestProcessorServlet-" + UUID.randomUUID());
    }

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        logger.info("RequestProcessorServlet.doGet()");

        response.setContentType("text/html");
        PrintWriter out = response.getWriter();

        out.println(UUID.randomUUID());
    }

    public String getServletInfo() {
        return "A servlet that knows the name of the person to whom it's" +
                UUID.randomUUID();
    }
}
