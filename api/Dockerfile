FROM hseeberger/scala-sbt:8u151-2.12.4-1.0.4
COPY . /usr/src
WORKDIR /usr/src
RUN sbt stage

FROM openjdk:8
WORKDIR /usr/app
COPY --from=0 /usr/src/target/universal/stage/ .

EXPOSE 9000

CMD /usr/app/bin/scrappy-api