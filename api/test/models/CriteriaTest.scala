package models

import java.time.Instant

import org.scalatestplus.play.PlaySpec
import reactivemongo.bson
import reactivemongo.bson.{BSONDateTime, BSONDocument, BSONString}

class CriteriaTest extends PlaySpec {

  "Criteria" should {

    "ignore empty criterion" in {
      Criteria.toQuery(List()) mustBe BSONDocument()
    }

    "be able to be converted to a Mongo Query" in {
      val date = Instant.parse("2017-12-21T22:30:00Z")

      val criterion = List(
        Criteria("author", "$text", Some(BSONString("mister"))),
        Criteria("timestamp", "$gte", None),
        Criteria("timestamp", "$lte", Some(BSONDateTime(date.toEpochMilli)))
      )

      Criteria.toQuery(criterion) mustBe bson.BSONDocument(
        "$and" -> List(
          BSONDocument("$text" -> BSONDocument("$search" -> "mister")),
          BSONDocument("timestamp" -> BSONDocument("$lte" -> BSONDateTime(date.toEpochMilli)))
        )
      )
    }
  }
}
