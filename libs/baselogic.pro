-obfuscationdictionary ./examples/dictionaries/compact.txt
-libraryjars /usr/java/j2sdk1.4.2_10/jre/lib/rt.jar
-injars fr.inria.ares.sfelixutils-0.1.jar
-outjar fr.inria.ares.sfelixutils-0.1-obs.jar
-dontshrink
-dontoptimize
-keep public class proguard.ProGuard {
public static void main(java.lang.String[]);
}