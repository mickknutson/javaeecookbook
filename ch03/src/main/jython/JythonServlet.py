import sys
from java.io import *
from java.util import Date
from com.baselogic.test import CustomerFixture
from javax.servlet.http import HttpServlet

class JythonServlet (HttpServlet):

    def doGet(self,request,response):
        self.doPost (request,response)

    def doPost(self,request,response):
        customer = CustomerFixture.createSingleCustomer()

        out = response.getWriter()
        response.setContentType ("text/html")
        out.println ("<html><head><title>Jython Servlet Test</title>" +
                          "<body><h1>Jython Servlet Test</h1>")

        out.println ("<p><b>Today is:</b>"+Date().toString()+"</p>")

        out.println ("<p><b>Java Customer:</b>"+customer.toString()+"</p>")

        out.println ("</body></html>")