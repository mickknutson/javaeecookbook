package com.baselogic.javaee6.service;

import com.baselogic.javaee6.domain.Abv;
import org.codehaus.jettison.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.ejb.Singleton;
import javax.enterprise.context.ApplicationScoped;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import java.text.DecimalFormat;

/**
 * AbvResource
 *
 * @author Mick Knutson
 * @see <a href="http://www.baselogic.com">Blog: http://baselogic.com</a>
 * @see <a href="http://linkedin.com/in/mickknutson">LinkedIN: http://linkedin.com/in/mickknutson</a>
 * @see <a href="http://twitter.com/mickknutson">Twitter: http://twitter.com/mickknutson</a>
 * @see <a href="http://github.com/mickknutson">Git hub: http://github.com/mickknutson</a>
 *
 * @see <a href="http://www.packtpub.com/authors/profiles/mick-knutson">Packt Author Profile</a>
 * @see <a href="http://www.packtpub.com/java-ee6-securing-tuning-extending-enterprise-applications-cookbook/book">JavaEE 6 Cookbook Packt</a>
 * @see <a href="http://www.amazon.com/Cookbook-securing-extending-enterprise-applications/dp/1849683166">JavaEE 6 Cookbook Amazon</a>
 *
 * @since 2012
 *
 */
@Path("abv")
public class AbvResource {

    private static final Logger logger = LoggerFactory.getLogger(AbvResource.class);


    private static DecimalFormat format1 = new DecimalFormat("#.#");
    private static DecimalFormat format2 = new DecimalFormat("#.##");
    private static DecimalFormat format3 = new DecimalFormat("#.###");

    /**
     * POST:
     * http://127.0.0.1:8080/ch06-web-mobile/services/abv/calculateAbv?abv_r=3.2&volume_r=16&price_r=5.99
     *
     * @param abv
     * @param volume
     * @param price
     * @return
     */
    @GET
    @Produces({MediaType.APPLICATION_JSON})
    public Abv calculateAbv(@QueryParam(value = "abv_r") @DefaultValue("0.0") final double abv,
                            @QueryParam(value = "volume_r") @DefaultValue("12") final int volume,
                            @QueryParam(value = "price_r") @DefaultValue("0.00") final double price
    ) {
        Abv valueObject = createAbvObject(abv, volume, price);

        logger.info("valueObject: {}", valueObject);
        return valueObject;
    }

    /**
     * POST:
     * http://127.0.0.1:8080/ch06-web-mobile/services/abv/calculateCallback?callback=jQuery16405310554881580174_1336231830153&abv_r=3.2&volume_r=16&price_r=5.99
     *
     * @param callback callback
     * @param volume volume_r
     * @param abv abv_r
     * @param price price_r
     * @return JSON object detailing the 'value' or price per abv %
     */
    @POST
    @Path("calculateCallback")
    @Produces({MediaType.APPLICATION_JSON})
    public String calculateCallback(@FormParam(value = "callback") final String callback,
                                    @FormParam(value = "abv_r") @DefaultValue("0.1") final double abv,
                                    @FormParam(value = "volume_r") @DefaultValue("12") final int volume,
                                    @FormParam(value = "price_r") @DefaultValue("0.01") final double price
    ) {
        logger.info("===== abv:: calculateCallback =====");
        logger.info("callback: {}", callback);
        logger.info("abv: {}", abv);
        logger.info("volume: {}", volume);
        logger.info("price: {}", price);

        //String jsonData = getDataAsJson(req.getParameter("symbol"));
        //String output = req.getParameter("callback") + "(" + jsonData + ");";

        String json2 = calculateAndMarshallAbvJson(abv, volume, price);

        Abv valueObject = createAbvObject(abv, volume, price);
        String json = marshallAbvJson(valueObject);

        logger.info("callback: {}", callback);

        logger.info("==========================");
        logger.info("json: {}", json);

        return "(" + json + ");";
    }

    /**
     * GET:
     * http://localhost:8080/ch06-web-mobile/services/abv/getCalculateCallback?callback=jQuery16405310554881580174_1336231830153&abv_r=3.2&volume_r=16&price_r=5.99
     *
     * @param callback
     * @param abv
     * @param volume
     * @param price
     * @return
     */
    @GET
    @Path("getCalculateCallback")
    @Produces({MediaType.APPLICATION_JSON})
    public String getCalculateCallback(@QueryParam(value = "callback") final String callback,
                                       @QueryParam(value = "abv_r") @DefaultValue("0.1") final double abv,
                                       @QueryParam(value = "volume_r") @DefaultValue("12") final int volume,
                                       @QueryParam(value = "price_r") @DefaultValue("0.01") final double price
    ) {
        //String jsonData = getDataAsJson(req.getParameter("symbol"));
        //String output = req.getParameter("callback") + "(" + jsonData + ");";

        Abv valueObject = createAbvObject(abv, volume, price);
        String json = marshallAbvJson(valueObject);

        logger.info("callback: {}", callback);

        logger.info("==========================");
        logger.info("json: {}", json);

        //out.print(callBack + "(" + statham + ");")
        return callback + "(" + json + ");";
    }


    /*@POST
    @Path("/post")
    @Consumes(MediaType.APPLICATION_JSON)
    public Response createInJSON(Abv abv) {

        String result = "Abv saved : " + abv;
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
    private String calculateAndMarshallAbvJson(double abv, int volume, double price) {
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

    private String marshallAbvJson(Abv valueObject) {
        StringBuilder sb = new StringBuilder();
        //{"abv_r": "12.4", "volume_r": "12", "price_r": "5.00","value": ".0117","score": "12"}
        sb.append("{");
        sb.append("\"abv\":").append("\"").append(valueObject.getAbv()).append("\"").append(",");
        sb.append("\"volume\":").append("\"").append(valueObject.getVolume()).append("\"").append(",");
        sb.append("\"price\":").append("\"").append(valueObject.getPrice()).append("\"").append(",");
        sb.append("\"value\":").append("\"").append(valueObject.getValue()).append("\"").append(",");
        sb.append("\"score\":").append("\"").append(valueObject.getScore()).append("\"");
        sb.append("}");
        return sb.toString();
    }

    private Abv createAbvObject(final double abv,
                                final int volume,
                                final double price) {
        logger.info("===== abv:: createAbvObject =====");
        logger.info("abv: {}", abv);
        logger.info("volume: {}", volume);
        logger.info("price: {}", price);

        double abv_r = Double.valueOf(format2.format(abv));
        int volume_r = Math.round(volume);
        double price_r = Double.valueOf(format3.format(price));

        logger.info("abv_r: {}", abv_r);
        logger.info("volume_r: {}", volume_r);
        logger.info("price_r: {}", price_r);

        double value_r = Double.valueOf(format3.format((price_r / volume_r) / abv_r));
        logger.info("value_r: {}", value_r);

        double score_r = Double.valueOf(format1.format(Math.round(value_r * 100)));
        logger.info("score_r: {}", score_r);

        return new Abv(
                abv_r,
                volume_r,
                price_r,
                value_r,
                score_r
        );
    }

}