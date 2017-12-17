name := """scrappy-api"""
organization := "com.github.athieriot"

version := "1.0-SNAPSHOT"

lazy val root = (project in file(".")).enablePlugins(PlayScala)

scalaVersion := "2.12.3"

libraryDependencies ++= Seq(
  guice,
  "org.reactivemongo"      %% "play2-reactivemongo"   % "0.12.7-play26",
  "org.scalamock"          %% "scalamock"             % "4.0.0"           % Test,
  "org.scalatestplus.play" %% "scalatestplus-play"    % "3.1.2"           % Test,
  "com.github.simplyscala" %% "scalatest-embedmongo"  % "0.2.4"           % "test"
)

import play.sbt.routes.RoutesKeys

RoutesKeys.routesImport += "play.modules.reactivemongo.PathBindables._"

coverageExcludedPackages := "controllers\\.Reverse.*;controllers\\.Reverse.*;controllers\\.javascript.*;router.*"

coverageMinimum := 90

coverageFailOnMinimum := true