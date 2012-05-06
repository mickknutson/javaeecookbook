package com.baselogic.javaee6.service;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;

/**
 * MobileResource
 *
 * To see all the REST services:
 * http://localhost:8080/ch06-web-mobile/services/application.wadl
 *
 * http://localhost:8080/ch06-web-mobile/services/abv?abv_r=3.2&volume_r=16&price_r=5.99
 * http://localhost:8080/ch06-web-mobile/com.baselogic.javaee6.service/services/abv?abv_r=3.2&volume_r=16&price_r=5.99
 *
 *
 * @author Mick Knutson
 * @see <a href="http://www.baselogic.com">Blog: http://baselogic.com</a>
 * @see <a href="http://linkedin.com/in/mickknutson">LinkedIN: http://linkedin.com/in/mickknutson</a>
 * @see <a href="http://twitter.com/mickknutson">Twitter: http://twitter.com/mickknutson</a>
 * @see <a href="http://github.com/mickknutson">Git hub: http://github.com/mickknutson</a>
 *
 * @see <a href="http://www.packtpub.com/java-ee6-securing-tuning-extending-enterprise-applications-cookbook/book">JavaEE 6 Cookbook Packt</a>
 * @see <a href="http://www.amazon.com/Cookbook-securing-extending-enterprise-applications/dp/1849683166">JavaEE 6 Cookbook Amazon</a>
 *
 * @since 2012
 *
 */
@Path("mobile")
public class MobileResource {

    private static final Logger logger = LoggerFactory.getLogger(MobileResource.class);

    /**
     * Not available as cross domain call.
     * @param abv_r
     * @param volume_r
     * @param price_r
     * @return
     */
    @POST
    @Path("calculate")
    @Produces({MediaType.APPLICATION_JSON})
    public String calculate(@FormParam(value = "abv_r") @DefaultValue("0.0") final float abv_r,
                            @FormParam(value = "volume_r") @DefaultValue("12") final int volume_r,
                            @FormParam(value = "price_r") @DefaultValue("0.00") final float price_r
    ) {
        logger.info("abv_r: {}", abv_r);
        logger.info("volume_r: {}", volume_r);
        logger.info("price_r: {}", price_r);

        return marshallAbvJson(abv_r, volume_r, price_r);
        //return calculateAbv(8.7F, 10, 5.00F);
    }

    /**
     *
     * Sample: http://localhost:8080/ch06-web-mobile/services/mobile/calculate?abv=3.2&volume=16&price=5.99
     * sample: http://localhost:8080/ch06-web-mobile/services/mobile/calculate?abv=3.2&volume=16&price=5.99
     * Sample: http://localhost:8080/ch06-web-mobile/services/mobile/json/4.5+10+5.00
     *
     * @param callback callback
     * @param volume_r volume_r
     * @param abv_r abv_r
     * @param price_r price_r
     * @return JSON object detailing the 'value' or price per abv %
     */
    @POST
    @Path("calculateCallback")
    @Produces({MediaType.APPLICATION_JSON})
    public String calculateCallback(@FormParam(value = "callback") final float callback,
                                    @FormParam(value = "abv_r") @DefaultValue("0.0") final float abv_r,
                                    @FormParam(value = "volume_r") @DefaultValue("12") final int volume_r,
                                    @FormParam(value = "price_r") @DefaultValue("0.00") final float price_r
    ) {
        //String jsonData = getDataAsJson(req.getParameter("symbol"));
	    //String output = req.getParameter("callback") + "(" + jsonData + ");";

        String json = marshallAbvJson(abv_r, volume_r, price_r);

        logger.info("abv_r: {}", abv_r);
        logger.info("volume_r: {}", volume_r);
        logger.info("price_r: {}", price_r);

        logger.info("==========================");
        logger.info("json: {}", json);

        return "(" + marshallAbvJson(abv_r, volume_r, price_r) + ");";
    }


    /*@POST
    @Path("/post")
    @Consumes(MediaType.APPLICATION_JSON)
    public Response createTrackInJSON(Track track) {

        String result = "Track saved : " + track;
        return Response.status(201).entity(result).build();

    }*/


    /**
     * Calculation is:
     * <i>cost = (price / volume) / abv;</i>
     * <p/>
     * Based on these values:
     * <i>price = $5.00</i>
     * <i>volume = 12 oz</i>
     * <i>abv = 8.7%</i>
     * <p/>
     * Which will give you:
     * <i>Value = $.034 per percent of alcohol in a 12 oz drink</i>
     * <p/>
     * If we compare the same 12oz drink with 3.2% abv, we get:
     * <i>Value = $.130 per percent of alcohol.</i>
     *
     * @param abv    Alcohol content
     * @param volume Size of drink
     * @param price  cost for the drink
     * @return JSON object detailing the 'value' or price per abv %
     */
    private String marshallAbvJson(float abv, int volume, float price) {
        StringBuilder sb = new StringBuilder();
        //{"volume_r": "12","abv_r": "12.4","price_r": "5.00"}
        sb.append("{");
        sb.append("\"abv_r\":").append("\"").append(abv).append("\"").append(",");
        sb.append("\"volume_r\":").append("\"").append(volume).append("\"").append(",");
        sb.append("\"price_r\":").append("\"").append(price).append("\"").append(",");
        sb.append("\"value_r\":").append("\"").append((price / volume) / abv).append("\"");
        sb.append("}");
        return sb.toString();
    }
}