package com.baselogic.chapter05.utils

import java.util.zip.ZipEntry
import java.util.zip.ZipOutputStream

public class ArchiveLogs {

    //def static ant = new AntBuilder()

    /**
     *
     * @param args
     */
    public static void main(String... args) {
        try {
            int age = 7

            List<File> originationDirectories = new ArrayList<File>()
            File destination

            if (args.length >= 3) {
                age = Integer.valueOf(args[0])

                if (age > 0
                        && args[1] != ''
                        && args[2] != ''
                ) {
                    println("--- Arguments: ---")
                    println("Age in days: " + args[0])
                    println("Destination: " + args[1])
                    println("Originations: " + (args.length - 2))

                    destination = new File(args[1])

                    for (int i = 2; i < args.length; i++) {
                        println("--> orgination directory: " + args[i])
                        if (new File(args[i]).exists()) {
                            println("-->>> adding orgination directory: " + args[i])
                            originationDirectories.add(new File(args[i]))
                        } else {
                            println "*** " + args[i] + " does not exists. Omitting from archival. ***"
                        }
                    }
                    if (originationDirectories.size() > 0) {
                        if (validateDirectory(destination)) {
                            List<File> originationFiles = getFilesToArchive(originationDirectories, age)
                            // Move Files to temp
                            moveFilesAndCompress(originationFiles, destination)
                            //deleteFiles(originationFiles)
                        } else {
                            println destination + " does not exists. Omitting from archival."
                        }
                    }
                    else {
                        throw new Exception("Origination and Destination directory must exist.")
                    }
                }
                else {
                    throw new Exception("argument values are not valid")
                }
            }
            else {
                throw new Exception("Must provide 3 arguments.")
            }
        } catch (Exception e) {
            println("Exception:")
            println(e.getMessage())
            //e.printStackTrace()
            printHelp()
        }
    }

    private static List<File> getFilesToArchive(List<File> originationDirectories, int age) {
        List<File> originationFiles = new ArrayList<File>()
        println(">>> $originationDirectories.size <<<")
        originationDirectories.each {
            originationFiles.addAll(getFilesOlderThan(it, age))
        }
        return originationFiles
    }

    private static void moveFilesAndCompress(List<File> originationFiles, File destination) {

        println("--- move $originationFiles.size Files to $destination.name ---")
        println("------------------------------")

        if (originationFiles.size > 0) {
            String zipFileName = String.format('%tF', new Date()) + "__files-contained-" + originationFiles.size + ".zip"
            File f = new File(destination.absolutePath, zipFileName)
            int incrementor = 0;
            while (f.exists()) {
                f = new File(destination.absolutePath,
                        String.format('%tF', new Date()) + "__files-contained-" + originationFiles.size + "_" + (++incrementor) + ".zip"
                )
            }

            FileOutputStream fos = new FileOutputStream(f)
            ZipOutputStream zipFile = new ZipOutputStream(fos)

            try{

                originationFiles.each {
                    println "--> File to archive: $it"

                    def name = (it.absolutePath =~ /[:\\]/).replaceAll("__")
                    println "--> archive name: $name"
                    ZipEntry zipEntry = new ZipEntry(name)
                    zipEntry.setComment(it.absolutePath)

                    zipFile.putNextEntry(zipEntry)
                    def buffer = new byte[1024]
                    it.withInputStream { i ->
                        def count
                        while ((count = i.read(buffer, 0, 1024)) != -1) {
                            zipFile.write(buffer)
                        }
                        i.close()
                    }
                    zipFile.closeEntry()
                } // End File Loop.

            } catch(Exception e){
                e.printStackTrace()
            } finally{
                zipFile.flush()
                zipFile.close()
                zipFile = null

                fos.flush()
                fos.close()
                fos = null
                System.gc();
            }

            deleteFiles(originationFiles)

            println("------------------------------")
            println "$originationFiles.size files."
            println "zipped files into " + zipFileName
            println "moved to " + destination.getPath()
            println("------------------------------")
        }
        else {
            println("--->>> no files to move <<<---")
        }
    }

    public static boolean validateDirectory(File directory) {
        return directory.exists()
    }

    public static void deleteFiles(List<File> originationFiles) {
        println "--> deleting archived files on exit"
        originationFiles.each {
            it.deleteOnExit()
        }
    }

    /*public static boolean deleteFile(File f) {
        File fileToDelete = f;
        println "fileToDelete.exists(): " + fileToDelete.exists();
        println "fileToDelete.isFile(): " + fileToDelete.isFile();
        println "fileToDelete.canWrite(): " + fileToDelete.canWrite();

        fileToDelete.deleteOnExit()
        if (fileToDelete.delete() || fileToDelete.deleteOnExit()) {
          println "Deletion of file '" + fileToDelete + "' was successful."
            return true
        }
        else {
          println "Deletion of file '" + fileToDelete + "' FAILED."
            return false
        }
    }*/

    public static void printHelp() {
        println("------------------------------")
        println("--- Archive Logger Help ---")
        println("------------------------------")
        println("Usage:")
        println("java -jar log-archiver.jar ArchiveLogs [age] [destination] [originations[1..*]]")
        println("------------------------------")
        println("Age: age in days to keep logs (must be > 0). Files older than this will be moved.")
        println("Destinatio: The fully path to the log directory to move files TO")
        println("Originations: The fully path to the log directory to move files FROM")
        println("------------------------------")
    }

    private static ArrayList<File> getFilesOlderThan(File dir, int age) {
        List<File> files = new ArrayList<File>();
        if (dir.exists() && dir.isDirectory()) {
            dir.listFiles().each {
                File file = it;
                if (file.isDirectory()) {
                    println "do not traverse"
                } else {
                    if (isOlderThanAge(file.lastModified(), age)) {
                        files.add(file);
                    }
                }
            }
        } else {
            //files.add(dir);
        }
        println ">>> processed $dir.absolutePath <<"
        return files;
    }

    static Long DAY_IN_MILLIS = 86400000;

    private static boolean isOlderThanAge(long lastModifiedDate, int age) {
        def ageAgo = (new Date()).time - ((1000 * 60 * 60 * 24) * age)

        return (lastModifiedDate <= ageAgo)
    }

}
//---------------------------------------------------------------------------//
