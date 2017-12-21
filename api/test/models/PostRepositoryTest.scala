package models

import com.github.simplyscala.MongoEmbedDatabase
import org.scalatestplus.play.PlaySpec
import org.scalatestplus.play.guice.GuiceOneAppPerTest
import play.api.inject.guice.GuiceApplicationBuilder
import reactivemongo.bson.BSONObjectID
import scala.concurrent.ExecutionContext.Implicits.global

class PostRepositoryTest extends PlaySpec
  with GuiceOneAppPerTest
  with MongoEmbedDatabase {

  override def fakeApplication() = new GuiceApplicationBuilder()
    .configure("mongodb.uri" -> "mongodb://localhost:12345/test")
    .build()

  "repository" should {

    "be able to retrieve a list of posts" in {
      withEmbedMongoFixture() { _ =>
        val repository = fakeApplication().injector.instanceOf[PostRepository]
        val expected = Post("UUID", Some("Test"), Some("Me"), Some("2017"))

        repository.add(expected)
        repository.find(None, None, None) map { p =>
          assert(p.length == 1)
          assert(p == expected)
        }
      }
    }

    "be able to retrieve one post" in {
      withEmbedMongoFixture() { _ =>
        val repository = fakeApplication().injector.instanceOf[PostRepository]
        val id = "sjhdfsd54f5s4df"
        val expected = Post(id, Some("Test"), Some("Me"), Some("2017"))

        repository.add(expected)
        repository.getOne(id) map { p =>
          assert(p == expected)
        }
      }
    }
  }
}
