<%--
invoke this XSS test with the following commands:

http://localhost:8080/ch03/xssTest.jsp?USERINPUT=Hello

Then try malicious inputs

http://localhost:8080/ch03/xssTest.jsp?USERINPUT=<script>alert('XSS Attack!');</script>
--%>
<% String input = request.getParameter("USERINPUT"); %>

User Input: <%= input %>
